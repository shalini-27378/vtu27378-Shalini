package com.example.myproject.repository;

import com.example.myproject.entity.Resident;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;
import java.time.LocalDate;
import java.util.List;

@Repository
public interface ResidentRepository extends JpaRepository<Resident, Long> {  // Changed from Integer to Long
    
    // Existing methods
    List<Resident> findByEmail(String email);
    List<Resident> findByNameContaining(String name);
    List<Resident> findByPNo(String pNo);
    
    // ============= PHASE 4: PAGINATION METHODS =============
    
    // Paginated version of findByRoomId
    Page<Resident> findByRoomId(Integer roomId, Pageable pageable);
    
    // Paginated version of findByCheckoutDateIsNull
    Page<Resident> findByCheckoutDateIsNull(Pageable pageable);
    
    // Paginated version of findByCheckoutDateIsNotNull
    Page<Resident> findByCheckoutDateIsNotNull(Pageable pageable);
    
    // Paginated search with custom query
    @Query("SELECT r FROM Resident r WHERE LOWER(r.name) LIKE LOWER(CONCAT('%', :searchTerm, '%')) OR LOWER(r.email) LIKE LOWER(CONCAT('%', :searchTerm, '%'))")
    Page<Resident> searchByNameOrEmailPaginated(@Param("searchTerm") String searchTerm, Pageable pageable);
    
    // Find residents who joined between dates with pagination
    Page<Resident> findByJoinDateBetween(LocalDate startDate, LocalDate endDate, Pageable pageable);
    
    // Existing methods (keep these)
    List<Resident> findByRoomId(Integer roomId);
    List<Resident> findByCheckoutDateIsNull();
    List<Resident> findByCheckoutDateIsNotNull();
    List<Resident> findByJoinDateAfter(LocalDate date);
    
    @Query("SELECT r FROM Resident r WHERE LOWER(r.name) LIKE LOWER(CONCAT('%', :searchTerm, '%')) OR LOWER(r.email) LIKE LOWER(CONCAT('%', :searchTerm, '%'))")
    List<Resident> searchByNameOrEmail(@Param("searchTerm") String searchTerm);
    
    @Query("SELECT r FROM Resident r WHERE r.roomId = :roomId AND r.checkoutDate IS NULL")
    List<Resident> findActiveResidentsByRoom(@Param("roomId") Integer roomId);
}